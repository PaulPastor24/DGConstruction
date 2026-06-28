'use client'

import { makeStyles } from '@fluentui/react-components'

const useStyles = makeStyles({
  root: {
    display: 'grid',
    gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))',
    gap: '24px',
  },
  card: {
    backgroundColor: '#FFFFFF',
    borderRadius: '16px',
    padding: '28px',
    boxShadow: '0 1px 3px rgba(0, 0, 0, 0.06)',
    border: '1px solid rgba(0, 0, 0, 0.08)',
    transition: 'all 0.3s ease',
    '&:hover': {
      boxShadow: '0 4px 12px rgba(0, 0, 0, 0.1)',
      transform: 'translateY(-2px)',
    },
  },
  cardActive: {
    backgroundColor: 'rgba(102, 187, 106, 0.08)',
    borderTop: '4px solid #66BB6A',
  },
  phaseIcon: {
    fontSize: '32px',
    marginBottom: '16px',
  },
  phaseName: {
    fontSize: '18px',
    fontWeight: 700,
    color: '#333333',
    marginBottom: '4px',
  },
  phaseStatus: {
    fontSize: '12px',
    fontWeight: 600,
    color: '#999999',
    textTransform: 'uppercase',
    letterSpacing: '0.5px',
    marginBottom: '12px',
  },
  progressBar: {
    height: '10px',
    backgroundColor: '#EEEEEA',
    borderRadius: '8px',
    overflow: 'hidden',
    marginBottom: '16px',
  },
  progressFill: {
    height: '100%',
    backgroundColor: '#66BB6A',
    transition: 'width 0.5s ease',
  },
  progressLabel: {
    display: 'flex',
    justifyContent: 'space-between',
    alignItems: 'center',
    fontSize: '13px',
    fontWeight: 600,
    color: '#333333',
    marginBottom: '16px',
  },
  detailRow: {
    display: 'flex',
    justifyContent: 'space-between',
    fontSize: '12px',
    paddingTop: '8px',
    paddingBottom: '8px',
    borderBottom: '1px solid rgba(0, 0, 0, 0.05)',
    '&:last-child': {
      borderBottom: 'none',
    },
  },
  detailLabel: {
    color: '#999999',
    fontWeight: 500,
  },
  detailValue: {
    color: '#333333',
    fontWeight: 600,
  },
})

const phases = [
  {
    name: 'Foundation',
    icon: '🏗️',
    status: 'Completed',
    progress: 100,
    plannedStart: 'Apr 15',
    plannedEnd: 'May 10',
    actualEnd: 'May 8',
    milestones: 3,
    active: false,
  },
  {
    name: 'Framing',
    icon: '🪵',
    status: 'Completed',
    progress: 100,
    plannedStart: 'May 11',
    plannedEnd: 'Jun 4',
    actualEnd: 'Jun 3',
    milestones: 4,
    active: false,
  },
  {
    name: 'Roofing',
    icon: '🏠',
    status: 'Active',
    progress: 65,
    plannedStart: 'Jun 5',
    plannedEnd: 'Jul 10',
    actualEnd: 'Ongoing',
    milestones: 5,
    active: true,
  },
  {
    name: 'Finishing',
    icon: '🎨',
    status: 'Pending',
    progress: 0,
    plannedStart: 'Jul 11',
    plannedEnd: 'Aug 20',
    actualEnd: 'Pending',
    milestones: 6,
    active: false,
  },
  {
    name: 'Inspection',
    icon: '✓',
    status: 'Pending',
    progress: 0,
    plannedStart: 'Aug 21',
    plannedEnd: 'Sep 05',
    actualEnd: 'Pending',
    milestones: 2,
    active: false,
  },
]

export function PhaseOverviewGrid() {
  const styles = useStyles()

  return (
    <div className={styles.root}>
      {phases.map((phase) => (
        <div key={phase.name} className={`${styles.card} ${phase.active ? styles.cardActive : ''}`}>
          <div className={styles.phaseIcon}>{phase.icon}</div>
          <div className={styles.phaseName}>{phase.name}</div>
          <div className={styles.phaseStatus}>{phase.status}</div>

          <div className={styles.progressBar}>
            <div className={styles.progressFill} style={{ width: `${phase.progress}%` }} />
          </div>

          <div className={styles.progressLabel}>
            <span>Progress</span>
            <span>{phase.progress}%</span>
          </div>

          <div className={styles.detailRow}>
            <span className={styles.detailLabel}>Planned</span>
            <span className={styles.detailValue}>{phase.plannedStart} - {phase.plannedEnd}</span>
          </div>
          <div className={styles.detailRow}>
            <span className={styles.detailLabel}>Ended</span>
            <span className={styles.detailValue}>{phase.actualEnd}</span>
          </div>
          <div className={styles.detailRow}>
            <span className={styles.detailLabel}>Milestones</span>
            <span className={styles.detailValue}>{phase.milestones}</span>
          </div>
        </div>
      ))}
    </div>
  )
}
