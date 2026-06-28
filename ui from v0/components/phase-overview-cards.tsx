'use client'

import { makeStyles } from '@fluentui/react-components'

const useStyles = makeStyles({
  root: {
    display: 'flex',
    flexDirection: 'column',
    gap: '8px',
  },
  header: {
    marginBottom: '24px',
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
  grid: {
    display: 'grid',
    gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))',
    gap: '20px',
  },
  card: {
    backgroundColor: '#FFFFFF',
    borderRadius: '16px',
    padding: '24px',
    boxShadow: '0 1px 3px rgba(0, 0, 0, 0.06)',
    border: '1px solid rgba(0, 0, 0, 0.08)',
    borderLeft: '4px solid #DDDDDD',
    transition: 'all 0.3s ease',
    cursor: 'pointer',
    '&:hover': {
      boxShadow: '0 4px 12px rgba(0, 0, 0, 0.1)',
      transform: 'translateY(-2px)',
    },
  },
  cardActive: {
    borderLeft: '4px solid #66BB6A',
    backgroundColor: 'rgba(102, 187, 106, 0.04)',
  },
  header2: {
    display: 'flex',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: '16px',
  },
  phaseName: {
    fontSize: '16px',
    fontWeight: 700,
    color: '#333333',
  },
  statusBadge: {
    display: 'inline-block',
    paddingTop: '4px',
    paddingBottom: '4px',
    paddingLeft: '10px',
    paddingRight: '10px',
    borderRadius: '16px',
    fontSize: '11px',
    fontWeight: 700,
    textTransform: 'uppercase',
    backgroundColor: '#EEEEEA',
    color: '#666666',
  },
  statusBadgeCompleted: {
    backgroundColor: 'rgba(102, 187, 106, 0.15)',
    color: '#2F6B3C',
  },
  statusBadgeCurrent: {
    backgroundColor: 'rgba(47, 107, 60, 0.15)',
    color: '#2F6B3C',
  },
  progressBar: {
    height: '6px',
    backgroundColor: '#EEEEEA',
    borderRadius: '3px',
    overflow: 'hidden',
    marginBottom: '12px',
  },
  progressFill: {
    height: '100%',
    backgroundColor: '#66BB6A',
    transition: 'width 0.5s ease',
  },
  content: {
    display: 'flex',
    flexDirection: 'column',
    gap: '12px',
  },
  row: {
    display: 'flex',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  label: {
    fontSize: '12px',
    color: '#999999',
    fontWeight: 600,
  },
  value: {
    fontSize: '14px',
    fontWeight: 600,
    color: '#333333',
  },
})

interface PhaseCard {
  id: string
  name: string
  status: 'completed' | 'current' | 'pending'
  progress: number
  plannedStart: string
  actualStart: string
  plannedEnd: string
  milestones: number
}

const phases: PhaseCard[] = [
  {
    id: '1',
    name: 'Foundation',
    status: 'completed',
    progress: 100,
    plannedStart: 'May 1',
    actualStart: 'May 1',
    plannedEnd: 'May 28',
    milestones: 2,
  },
  {
    id: '2',
    name: 'Framing',
    status: 'completed',
    progress: 100,
    plannedStart: 'May 29',
    actualStart: 'May 29',
    plannedEnd: 'Jun 20',
    milestones: 3,
  },
  {
    id: '3',
    name: 'Roofing',
    status: 'current',
    progress: 65,
    plannedStart: 'Jun 21',
    actualStart: 'Jun 21',
    plannedEnd: 'Jul 10',
    milestones: 4,
  },
  {
    id: '4',
    name: 'Finishing',
    status: 'pending',
    progress: 0,
    plannedStart: 'Jul 11',
    actualStart: '-',
    plannedEnd: 'Aug 05',
    milestones: 3,
  },
  {
    id: '5',
    name: 'Inspection',
    status: 'pending',
    progress: 0,
    plannedStart: 'Aug 06',
    actualStart: '-',
    plannedEnd: 'Aug 20',
    milestones: 2,
  },
]

export function PhaseOverviewCards() {
  const styles = useStyles()

  return (
    <div className={styles.root}>
      <div className={styles.header}>
        <div className={styles.title}>Construction Phases</div>
        <div className={styles.subtitle}>Overview of all project phases</div>
      </div>

      <div className={styles.grid}>
        {phases.map((phase) => (
          <div key={phase.id} className={`${styles.card} ${phase.status === 'current' ? styles.cardActive : ''}`}>
            <div className={styles.header2}>
              <div className={styles.phaseName}>{phase.name}</div>
              <div className={`${styles.statusBadge} ${phase.status === 'completed' ? styles.statusBadgeCompleted : phase.status === 'current' ? styles.statusBadgeCurrent : ''}`}>
                {phase.status === 'current' ? 'Active' : phase.status === 'completed' ? 'Done' : 'Pending'}
              </div>
            </div>

            {phase.progress > 0 && (
              <div>
                <div className={styles.progressBar}>
                  <div className={styles.progressFill} style={{ width: `${phase.progress}%` }} />
                </div>
              </div>
            )}

            <div className={styles.content}>
              <div className={styles.row}>
                <span className={styles.label}>Planned</span>
                <span className={styles.value}>{phase.plannedStart} to {phase.plannedEnd}</span>
              </div>
              <div className={styles.row}>
                <span className={styles.label}>Progress</span>
                <span className={styles.value}>{phase.progress}%</span>
              </div>
              <div className={styles.row}>
                <span className={styles.label}>Milestones</span>
                <span className={styles.value}>{phase.milestones} Tasks</span>
              </div>
            </div>
          </div>
        ))}
      </div>
    </div>
  )
}
