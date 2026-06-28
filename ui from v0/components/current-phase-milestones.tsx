'use client'

import { makeStyles } from '@fluentui/react-components'

const useStyles = makeStyles({
  root: {
    backgroundColor: '#FFFFFF',
    borderRadius: '16px',
    padding: '32px',
    boxShadow: '0 1px 3px rgba(0, 0, 0, 0.06)',
    border: '1px solid rgba(0, 0, 0, 0.08)',
  },
  header: {
    marginBottom: '28px',
  },
  title: {
    fontSize: '20px',
    fontWeight: 700,
    color: '#2F6B3C',
    marginBottom: '6px',
  },
  subtitle: {
    fontSize: '14px',
    color: '#999999',
    fontWeight: 400,
  },
  timeline: {
    display: 'flex',
    flexDirection: 'column',
    gap: '16px',
  },
  milestoneItem: {
    display: 'grid',
    gridTemplateColumns: '40px 1fr 100px 100px 100px',
    gap: '24px',
    alignItems: 'center',
    paddingTop: '16px',
    paddingBottom: '16px',
    paddingLeft: '16px',
    paddingRight: '16px',
    borderRadius: '10px',
    transition: 'all 0.2s ease',
    '&:hover': {
      backgroundColor: 'rgba(0, 0, 0, 0.02)',
    },
    '@media (max-width: 900px)': {
      gridTemplateColumns: '40px 1fr',
      gap: '16px',
    },
  },
  itemCompleted: {
    backgroundColor: 'rgba(102, 187, 106, 0.08)',
    borderLeft: '3px solid #66BB6A',
  },
  itemActive: {
    backgroundColor: 'rgba(47, 107, 60, 0.08)',
    borderLeft: '3px solid #2F6B3C',
  },
  itemPending: {
    borderLeft: '3px solid #EEEEEA',
  },
  indicator: {
    width: '32px',
    height: '32px',
    borderRadius: '50%',
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'center',
    fontWeight: 700,
    fontSize: '16px',
  },
  indicatorCompleted: {
    backgroundColor: '#66BB6A',
    color: '#FFFFFF',
  },
  indicatorActive: {
    backgroundColor: '#2F6B3C',
    color: '#FFFFFF',
  },
  indicatorPending: {
    backgroundColor: '#EEEEEA',
    color: '#999999',
  },
  milestoneName: {
    fontSize: '14px',
    fontWeight: 600,
    color: '#333333',
  },
  column: {
    fontSize: '13px',
    fontWeight: 500,
    color: '#666666',
    textAlign: 'center',
  },
  columnLabel: {
    fontSize: '11px',
    fontWeight: 700,
    color: '#999999',
    textTransform: 'uppercase',
    letterSpacing: '0.5px',
    marginBottom: '8px',
  },
  '@media (max-width: 900px)': {
    columnHidden: {
      display: 'none',
    },
  },
})

const milestones = [
  {
    name: 'Roof Decking Installation',
    targetDate: 'Jun 28',
    status: 'Completed',
    completion: 100,
    daysRemaining: 0,
  },
  {
    name: 'Waterproofing Application',
    targetDate: 'Jul 5',
    status: 'Active',
    completion: 40,
    daysRemaining: 7,
  },
  {
    name: 'Shingle Installation',
    targetDate: 'Jul 10',
    status: 'Pending',
    completion: 0,
    daysRemaining: 12,
  },
  {
    name: 'Flashing & Gutters',
    targetDate: 'Jul 15',
    status: 'Pending',
    completion: 0,
    daysRemaining: 17,
  },
  {
    name: 'Final Roof Inspection',
    targetDate: 'Jul 20',
    status: 'Pending',
    completion: 0,
    daysRemaining: 22,
  },
]

export function CurrentPhaseMilestones() {
  const styles = useStyles()

  const getStatusClass = (status: string) => {
    if (status === 'Completed') return styles.itemCompleted
    if (status === 'Active') return styles.itemActive
    return styles.itemPending
  }

  const getIndicatorClass = (status: string) => {
    if (status === 'Completed') return styles.indicatorCompleted
    if (status === 'Active') return styles.indicatorActive
    return styles.indicatorPending
  }

  const getIndicatorLabel = (status: string) => {
    if (status === 'Completed') return '✓'
    if (status === 'Active') return '◉'
    return '○'
  }

  return (
    <div className={styles.root}>
      <div className={styles.header}>
        <div className={styles.title}>Current Phase Milestones</div>
        <div className={styles.subtitle}>Roofing phase timeline and milestone progress</div>
      </div>

      <div>
        <div className={styles.headerRow}>
          <div></div>
          <div style={{ fontSize: '11px', fontWeight: 700, color: '#999999', textTransform: 'uppercase', letterSpacing: '0.5px' }}>Milestone</div>
          <div style={{ textAlign: 'center', fontSize: '11px', fontWeight: 700, color: '#999999', textTransform: 'uppercase', letterSpacing: '0.5px' }}>Target Date</div>
          <div style={{ textAlign: 'center', fontSize: '11px', fontWeight: 700, color: '#999999', textTransform: 'uppercase', letterSpacing: '0.5px' }}>Progress</div>
          <div style={{ textAlign: 'center', fontSize: '11px', fontWeight: 700, color: '#999999', textTransform: 'uppercase', letterSpacing: '0.5px' }}>Days Left</div>
        </div>

        <div className={styles.timeline}>
          {milestones.map((milestone) => (
            <div key={milestone.name} className={`${styles.milestoneItem} ${getStatusClass(milestone.status)}`}>
              <div className={`${styles.indicator} ${getIndicatorClass(milestone.status)}`}>
                {getIndicatorLabel(milestone.status)}
              </div>
              <div className={styles.milestoneName}>{milestone.name}</div>
              <div className={styles.column}>{milestone.targetDate}</div>
              <div className={styles.column}>{milestone.completion}%</div>
              <div className={styles.column}>{milestone.daysRemaining}</div>
            </div>
          ))}
        </div>
      </div>
    </div>
  )
}
